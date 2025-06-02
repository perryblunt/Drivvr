from flask import Flask, jsonify, request, session
from db import get_db_connection
import hashlib
import uuid
import re # For email validation
from datetime import datetime, timedelta, timezone
import secrets

app = Flask(__name__)
app.config['SECRET_KEY'] = 'your_temporary_secret_key_12345!' # Replace with a real secret key
app.config['PERMANENT_SESSION_LIFETIME'] = timedelta(days=14) # For "Remember Me"

# Basic email validation regex (simplified)
EMAIL_REGEX = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'

@app.route('/')
def hello():
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            cursor.execute("SELECT 1")
            result = cursor.fetchone()
        conn.close()
        if result:
             return jsonify(message="Hello, Python backend! DB Connection Successful.", db_test_result=result)
        else:
            return jsonify(message="Hello, Python backend! DB Connection Successful, but no result from query."), 200
    except Exception as e:
        return jsonify(message="Hello, Python backend! DB Connection FAILED.", error=str(e)), 500

@app.route('/signup', methods=['POST'])
def signup():
    data = request.get_json()
    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    username = data.get('username')
    firstname = data.get('firstname')
    lastname = data.get('lastname')
    email = data.get('email')
    password = data.get('password')
    phonenumber = data.get('phonenumber')
    workorschool = data.get('workorschool') # Assuming this column name
    gender = data.get('gender')
    moreinformation = data.get('moreinformation')

    # Validation
    if not username:
        return jsonify(error="Missing username"), 400
    if not email:
        return jsonify(error="Missing email"), 400
    if not password:
        return jsonify(error="Missing password"), 400
    
    if not re.match(EMAIL_REGEX, email):
        return jsonify(error="Invalid email format"), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Check if username exists
            cursor.execute("SELECT username FROM users WHERE username = %s", (username,))
            if cursor.fetchone():
                return jsonify(error="Username already exists"), 409 # 409 Conflict
            
            # Check if email exists
            cursor.execute("SELECT email FROM users WHERE email = %s", (email,))
            if cursor.fetchone():
                return jsonify(error="Email already registered"), 409

            # Password Hashing (md5 then sha256)
            hashed_password_md5 = hashlib.md5(password.encode('utf-8')).hexdigest()
            hashed_password_sha256 = hashlib.sha256(hashed_password_md5.encode('utf-8')).hexdigest()

            # Generate activation codes
            # Equivalent to md5(uniqid(rand(), true))
            activation = hashlib.md5(str(uuid.uuid4()).encode('utf-8')).hexdigest()
            activation2 = hashlib.md5(str(uuid.uuid4()).encode('utf-8')).hexdigest() # Second one for potential 'forgot password'

            # Insert new user
            # Ensuring all fields from the PHP script are considered.
            # The PHP script has: user_id (auto_increment), username, firstname, lastname, email, password, 
            # phonenumber, workorschool, gender, moreinformation, activation, activation2
            # Assuming 'workorschool' from form maps to 'work_school' in DB, or using 'workorschool' if that's the actual column
            
            sql = """INSERT INTO users 
                     (username, firstname, lastname, email, password, phonenumber, workorschool, gender, moreinformation, activation, activation2) 
                     VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
            # Note: Switched workorschool to work_school as it's more conventional for DBs
            # If 'workorschool' is indeed the column name, this should be reverted.
            # For now, let's stick to the prompt's initial assumption for 'workorschool'
            cursor.execute(sql, (
                username, firstname, lastname, email, hashed_password_sha256, 
                phonenumber, workorschool, gender, moreinformation, activation, activation2
            ))
            conn.commit()

        return jsonify(message="User registered successfully. Please check your email for activation."), 201

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred during registration.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/login', methods=['POST'])
def login():
    data = request.get_json()
    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    email = data.get('email')
    password = data.get('password')
    rememberme = data.get('rememberme', False) # Defaults to False if not provided

    if not email:
        return jsonify(error="Missing email"), 400
    if not password:
        return jsonify(error="Missing password"), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            cursor.execute("SELECT user_id, username, password, activation FROM users WHERE email = %s", (email,))
            user = cursor.fetchone()

            if not user:
                return jsonify(error="Invalid email or password"), 401 # Unauthorized

            # Verify password
            hashed_password_md5 = hashlib.md5(password.encode('utf-8')).hexdigest()
            hashed_password_sha256 = hashlib.sha256(hashed_password_md5.encode('utf-8')).hexdigest()

            if user['password'] != hashed_password_sha256:
                return jsonify(error="Invalid email or password"), 401

            # Check activation status
            # In the PHP script, successful activation sets 'activation' to an empty string.
            if user['activation'] != '':
                return jsonify(error="Account not activated. Please check your email."), 403 # Forbidden

            # Login successful, set session
            session['user_id'] = user['user_id']
            session['username'] = user['username']
            session['email'] = email # Or user['email'] if selected from DB

            if rememberme:
                session.permanent = True
            
            return jsonify(
                message="Login successful", 
                user={'user_id': user['user_id'], 'username': user['username'], 'email': email}
            ), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred during login.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/logout', methods=['POST'])
def logout():
    session.clear()
    return jsonify(message="Logged out successfully."), 200

@app.route('/forgot-password', methods=['POST'])
def forgot_password():
    data = request.get_json()
    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    email = data.get('email')
    if not email:
        return jsonify(error="Missing email"), 400

    if not re.match(EMAIL_REGEX, email):
        return jsonify(error="Invalid email format"), 400

    conn = None
    reset_token_for_response = None # For testing, to include in response
    reset_link_for_response = None  # For testing

    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            cursor.execute("SELECT user_id, email FROM users WHERE email = %s", (email,))
            user = cursor.fetchone()

            if user:
                # User found, generate token and expiry
                token = secrets.token_urlsafe(32)
                # Ensure expiry is in UTC if your DB stores naive datetimes as UTC
                # For simplicity, using timezone-aware datetime objects
                expiry_time = datetime.now(timezone.utc) + timedelta(hours=1)
                
                reset_token_for_response = token # Store for test response
                reset_link_for_response = f"https://yourdomain.com/reset-password?token={token}" # Store for test response

                # Store the token and expiry in the database
                # This assumes `forgotpassword_token` and `forgotpassword_expiry` columns exist
                sql = """UPDATE users SET 
                         forgotpassword_token = %s, 
                         forgotpassword_expiry = %s 
                         WHERE user_id = %s"""
                cursor.execute(sql, (token, expiry_time, user['user_id']))
                conn.commit()
        
        # Generic response regardless of whether user was found or not
        # Including token and link for testing as requested
        response_payload = {
            "message": "If your email address is in our database, you will receive a password reset link shortly."
        }
        if reset_token_for_response: # Add only if a token was generated (user found)
            response_payload["_test_token"] = reset_token_for_response
            response_payload["_test_reset_link"] = reset_link_for_response
            
        return jsonify(response_payload), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/reset-password', methods=['POST'])
def reset_password():
    data = request.get_json()
    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    token = data.get('token')
    new_password = data.get('new_password')
    confirm_password = data.get('confirm_password')

    if not token:
        return jsonify(error="Missing token"), 400
    if not new_password:
        return jsonify(error="Missing new password"), 400
    if not confirm_password:
        return jsonify(error="Missing confirmation password"), 400

    if new_password != confirm_password:
        return jsonify(error="Passwords do not match"), 400
    
    # Optional: Add password strength requirements here
    # For example, minimum length:
    # if len(new_password) < 8:
    #     return jsonify(error="Password must be at least 8 characters long"), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Find user by token and check expiry
            current_time_utc = datetime.now(timezone.utc)
            cursor.execute("""SELECT user_id, forgotpassword_expiry 
                              FROM users 
                              WHERE forgotpassword_token = %s""", (token,))
            user = cursor.fetchone()

            if not user:
                return jsonify(error="Invalid or expired token"), 400 # Or 404 Not Found

            # Ensure forgotpassword_expiry is timezone-aware for comparison
            # PyMySQL by default might return naive datetime if the DB column is naive.
            # If so, assume it's UTC as stored by the forgot-password route.
            expiry_time_from_db = user['forgotpassword_expiry']
            if isinstance(expiry_time_from_db, datetime) and expiry_time_from_db.tzinfo is None:
                expiry_time_from_db = expiry_time_from_db.replace(tzinfo=timezone.utc)


            if expiry_time_from_db < current_time_utc:
                return jsonify(error="Invalid or expired token"), 400 # Token has expired

            # Token is valid and not expired. Proceed to update password.
            hashed_password_md5 = hashlib.md5(new_password.encode('utf-8')).hexdigest()
            hashed_password_sha256 = hashlib.sha256(hashed_password_md5.encode('utf-8')).hexdigest()

            update_sql = """UPDATE users SET 
                            password = %s, 
                            forgotpassword_token = NULL, 
                            forgotpassword_expiry = NULL 
                            WHERE user_id = %s"""
            cursor.execute(update_sql, (hashed_password_sha256, user['user_id']))
            conn.commit()

            return jsonify(message="Password has been reset successfully."), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/activate-account', methods=['GET'])
def activate_account():
    email = request.args.get('email')
    activation_key = request.args.get('key')

    if not email:
        return jsonify(error="Missing email parameter"), 400
    if not activation_key:
        return jsonify(error="Missing activation key parameter"), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            cursor.execute("SELECT user_id, activation, activation2 FROM users WHERE email = %s", (email,))
            user = cursor.fetchone()

            if not user:
                return jsonify(error="Invalid activation link or user not found."), 404 # Or 400

            if user['activation'] == '':
                # This implies the account might have already been activated if '' is the activated state
                return jsonify(message="Account already activated."), 200 # Or 400 if considered an error

            if user['activation'] != activation_key:
                return jsonify(error="Invalid activation key."), 400
            
            # Key matches and account not yet activated (assuming '' is activated state)
            # Set activation and activation2 to empty string to mark as activated and invalidate keys
            update_sql = "UPDATE users SET activation = '', activation2 = '' WHERE user_id = %s"
            cursor.execute(update_sql, (user['user_id'],))
            conn.commit()

            return jsonify(message="Account activated successfully. You can now log in."), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred during account activation.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/verify-new-email', methods=['GET'])
def verify_new_email():
    token = request.args.get('token')

    if not token:
        return jsonify(error="Missing verification token"), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            current_time_utc = datetime.now(timezone.utc)
            # Fetch user based on the new_email_token and check expiry
            # Assumes new_email_address, new_email_token, new_email_token_expiry columns exist
            sql_select = """SELECT user_id, new_email_address, new_email_token_expiry 
                            FROM users 
                            WHERE new_email_token = %s"""
            cursor.execute(sql_select, (token,))
            user = cursor.fetchone()

            if not user:
                return jsonify(error="Invalid or expired verification link."), 400 # Or 404

            # Ensure new_email_token_expiry is timezone-aware for comparison
            expiry_time_from_db = user['new_email_token_expiry']
            if isinstance(expiry_time_from_db, datetime) and expiry_time_from_db.tzinfo is None:
                expiry_time_from_db = expiry_time_from_db.replace(tzinfo=timezone.utc)
            
            if expiry_time_from_db < current_time_utc:
                # Optionally, clear the expired token details here as well
                # update_expired_sql = """UPDATE users SET 
                #                         new_email_address = NULL, 
                #                         new_email_token = NULL, 
                #                         new_email_token_expiry = NULL 
                #                         WHERE user_id = %s"""
                # cursor.execute(update_expired_sql, (user['user_id'],))
                # conn.commit()
                return jsonify(error="Verification link has expired."), 400

            # Token is valid and not expired. Proceed to update email.
            new_email = user['new_email_address']
            if not new_email: # Should not happen if token is valid, but as a safeguard
                 return jsonify(error="No new email address found to verify."), 400


            # Before updating, ensure the new email is not already in use by another user (optional, but good practice)
            cursor.execute("SELECT user_id FROM users WHERE email = %s AND user_id != %s", (new_email, user['user_id']))
            if cursor.fetchone():
                # Clear the pending change for the current user as the target email is taken
                clear_pending_sql = """UPDATE users SET 
                                        new_email_address = NULL, 
                                        new_email_token = NULL, 
                                        new_email_token_expiry = NULL 
                                        WHERE user_id = %s"""
                cursor.execute(clear_pending_sql, (user['user_id'],))
                conn.commit()
                return jsonify(error="This email address is already in use by another account."), 409 # Conflict
            
            update_sql = """UPDATE users SET 
                            email = %s, 
                            new_email_address = NULL, 
                            new_email_token = NULL, 
                            new_email_token_expiry = NULL 
                            WHERE user_id = %s"""
            cursor.execute(update_sql, (new_email, user['user_id']))
            conn.commit()

            # Update email in session if user is currently logged in
            if 'user_id' in session and session['user_id'] == user['user_id']:
                session['email'] = new_email

            return jsonify(message="Email address updated and verified successfully."), 200

    except pymysql.err.OperationalError as e:
        if "Unknown column" in str(e):
             return jsonify(error="Database schema error: Required columns for email update are missing. Please contact support.", _details=str(e)), 500
        return jsonify(error="A database error occurred.", details=str(e)), 500
    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/profile', methods=['GET'])
def profile():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']
    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Select only the necessary and safe profile fields
            # Assuming 'workorschool' and 'profilepicture' are the correct column names
            sql = """SELECT username, firstname, lastname, email, phonenumber, 
                     workorschool, gender, moreinformation, profilepicture 
                     FROM users WHERE user_id = %s"""
            cursor.execute(sql, (user_id,))
            user_profile = cursor.fetchone()

            if not user_profile:
                # This case should ideally not happen if session user_id is valid
                # and refers to an existing user.
                session.clear() # Clear potentially corrupted session
                return jsonify(error="User not found or session corrupted."), 404 
            
            # Convert profilepicture path if needed, or return as is
            # For now, returning as stored in DB.
            # If profilepicture is NULL or empty, it will be returned as such.

            return jsonify(user_profile), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred while fetching profile data.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/update-username', methods=['POST'])
def update_username():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']
    data = request.get_json()

    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    new_username = data.get('new_username')

    if not new_username or not new_username.strip():
        return jsonify(error="New username cannot be empty."), 400
    
    new_username = new_username.strip() # Remove leading/trailing whitespace

    # Optional: Add other username validation rules (e.g., length, characters)
    # if len(new_username) < 3:
    #     return jsonify(error="Username must be at least 3 characters long."), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Check if the new username is already taken by ANOTHER user
            cursor.execute("SELECT user_id FROM users WHERE username = %s AND user_id != %s", (new_username, user_id))
            if cursor.fetchone():
                return jsonify(error="Username already taken. Please choose a different one."), 409 # Conflict

            # Update the username
            update_sql = "UPDATE users SET username = %s WHERE user_id = %s"
            cursor.execute(update_sql, (new_username, user_id))
            conn.commit()

            # Update username in session
            session['username'] = new_username
            
            return jsonify(message="Username updated successfully.", new_username=new_username), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred while updating username.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/request-email-change', methods=['POST'])
def request_email_change():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']
    current_user_email = session.get('email') # Assuming email is stored in session

    data = request.get_json()
    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    new_email = data.get('new_email')
    password = data.get('password')

    if not new_email:
        return jsonify(error="New email address cannot be empty."), 400
    if not password:
        return jsonify(error="Password is required to change email."), 400
    
    new_email = new_email.strip().lower() # Standardize email

    if not re.match(EMAIL_REGEX, new_email):
        return jsonify(error="Invalid new email format."), 400

    if new_email == current_user_email:
        return jsonify(error="New email address must be different from your current one."), 400

    conn = None
    verification_link_for_response = None
    verification_token_for_response = None

    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Verify current password
            cursor.execute("SELECT password FROM users WHERE user_id = %s", (user_id,))
            user_record = cursor.fetchone()

            if not user_record: # Should not happen if session is valid
                return jsonify(error="User not found."), 404 

            hashed_password_md5 = hashlib.md5(password.encode('utf-8')).hexdigest()
            hashed_password_sha256 = hashlib.sha256(hashed_password_md5.encode('utf-8')).hexdigest()

            if user_record['password'] != hashed_password_sha256:
                return jsonify(error="Incorrect password."), 403 # Forbidden

            # Check if the new email is already in use by ANOTHER user
            cursor.execute("SELECT user_id FROM users WHERE email = %s AND user_id != %s", (new_email, user_id))
            if cursor.fetchone():
                return jsonify(error="This email address is already in use by another account."), 409 # Conflict
            
            # All checks passed, generate token and store details
            token = secrets.token_urlsafe(32)
            expiry_time = datetime.now(timezone.utc) + timedelta(hours=1) # 1 hour expiry

            update_sql = """UPDATE users SET
                            new_email_address = %s,
                            new_email_token = %s,
                            new_email_token_expiry = %s
                            WHERE user_id = %s"""
            cursor.execute(update_sql, (new_email, token, expiry_time, user_id))
            conn.commit()

            verification_token_for_response = token
            verification_link_for_response = f"https://yourdomain.com/verify-new-email?token={token}"
            
            response_payload = {
                "message": "Email change requested. Please check your new email address for a verification link."
            }
            if verification_link_for_response: # Add for testing
                response_payload["_test_verification_token"] = verification_token_for_response
                response_payload["_test_verification_link"] = verification_link_for_response

            return jsonify(response_payload), 200

    except pymysql.err.OperationalError as e:
        if "Unknown column" in str(e):
             return jsonify(error="Database schema error: Required columns for email change are missing. Please contact support.", _details=str(e)), 500
        return jsonify(error="A database error occurred during email change request.", details=str(e)), 500
    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred while requesting email change.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/update-password', methods=['POST'])
def update_password():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']
    data = request.get_json()

    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    current_password = data.get('current_password')
    new_password = data.get('new_password')
    confirm_new_password = data.get('confirm_new_password')

    if not all([current_password, new_password, confirm_new_password]):
        return jsonify(error="Missing required password fields."), 400

    if new_password != confirm_new_password:
        return jsonify(error="New passwords do not match."), 400

    # Optional: Add password strength requirements for new_password here
    # if len(new_password) < 8:
    #     return jsonify(error="New password must be at least 8 characters long."), 400
    
    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Verify current password
            cursor.execute("SELECT password FROM users WHERE user_id = %s", (user_id,))
            user_record = cursor.fetchone()

            if not user_record: # Should not happen if session is valid
                return jsonify(error="User not found."), 404

            hashed_current_password_md5 = hashlib.md5(current_password.encode('utf-8')).hexdigest()
            hashed_current_password_sha256 = hashlib.sha256(hashed_current_password_md5.encode('utf-8')).hexdigest()

            if user_record['password'] != hashed_current_password_sha256:
                return jsonify(error="Incorrect current password."), 403 # Forbidden

            # Hash the new password
            hashed_new_password_md5 = hashlib.md5(new_password.encode('utf-8')).hexdigest()
            hashed_new_password_sha256 = hashlib.sha256(hashed_new_password_md5.encode('utf-8')).hexdigest()

            # Update the password in the database
            update_sql = "UPDATE users SET password = %s WHERE user_id = %s"
            cursor.execute(update_sql, (hashed_new_password_sha256, user_id))
            conn.commit()

            return jsonify(message="Password updated successfully."), 200

    except Exception as e:
        # Log the exception e for debugging
        return jsonify(error="An error occurred while updating password.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/trips/add', methods=['POST'])
def add_trip():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']
    data = request.get_json()

    if not data:
        return jsonify(error="Invalid JSON payload"), 400

    # Extract data
    departure = data.get('departure')
    dep_lat = data.get('departureLatitude')
    dep_lng = data.get('departureLongitude')
    destination = data.get('destination')
    dest_lat = data.get('destinationLatitude')
    dest_lng = data.get('destinationLongitude')
    price = data.get('price')
    seats_available = data.get('seatsavailable')
    regular = data.get('regular') # 'Y' or 'N'
    time_str = data.get('time') # "HH:MM" string
    more_info = data.get('moreinformation', "") # Optional

    # Basic Validations
    errors = {}
    if not departure: errors['departure'] = "Departure location is required."
    if departure and (dep_lat is None or dep_lng is None): 
        errors['departureCoordinates'] = "Departure coordinates (latitude, longitude) are required."
    
    if not destination: errors['destination'] = "Destination is required."
    if destination and (dest_lat is None or dest_lng is None): 
        errors['destinationCoordinates'] = "Destination coordinates (latitude, longitude) are required."

    price_float = None
    if price is None: errors['price'] = "Price is required."
    else:
        try:
            price_float = float(price)
            if price_float < 0: errors['price'] = "Price cannot be negative."
        except ValueError:
            errors['price'] = "Price must be a valid number."

    seats_int = None
    if seats_available is None: errors['seatsavailable'] = "Seats available is required."
    else:
        try:
            seats_int = int(seats_available)
            if seats_int <= 0: errors['seatsavailable'] = "Seats available must be a positive integer."
        except ValueError:
            errors['seatsavailable'] = "Seats available must be a valid integer."

    if regular not in ['Y', 'N']: errors['regular'] = "Regular field must be 'Y' or 'N'."
    
    if not time_str: 
        errors['time'] = "Time is required."
    else:
        try:
            # Validate time format "HH:MM"
            datetime.strptime(time_str, '%H:%M')
        except ValueError:
            errors['time'] = "Invalid time format. Please use HH:MM."


    # Conditional validation based on 'regular'
    trip_date_db = None # For storing in DB (date type or NULL)
    days_of_week = {'monday': 0, 'tuesday': 0, 'wednesday': 0, 'thursday': 0, 'friday': 0, 'saturday': 0, 'sunday': 0}

    if regular == 'Y':
        any_day_selected = False
        for day_key in days_of_week.keys():
            day_value_from_request = data.get(day_key)
            if day_value_from_request in [1, '1', True, 'Y', 'y_on_value']: # Accommodate various true-like values
                days_of_week[day_key] = 1
                any_day_selected = True
            elif day_value_from_request in [0, '0', False, None, 'N', 'n_on_value', 'y_off_value']: # Accommodate false/null
                 days_of_week[day_key] = 0
            # else:
                # errors[day_key] = f"Invalid value for {day_key}. Expected 0 or 1 (or boolean)."
                # PHP script seems to default to 0 if not present or not '1', so strict error not needed here.
                # Let's assume if not explicitly 1, it's 0.
                # The check for 'any_day_selected' handles the case where none are set to 1.
                # If the field is missing, data.get(day_key) will be None, making it 0.
        
        if not any_day_selected:
            errors['days'] = "For a regular trip, at least one day of the week must be selected."
    elif regular == 'N':
        trip_date_str_from_request = data.get('date')
        if not trip_date_str_from_request:
            errors['date'] = "Date is required for a one-off trip."
        else:
            try:
                datetime.strptime(trip_date_str_from_request, '%Y-%m-%d')
                trip_date_db = trip_date_str_from_request
            except ValueError:
                errors['date'] = "Invalid date format for one-off trip. Please use YYYY-MM-DD."
    
    if errors:
        return jsonify(error="Validation failed", messages=errors), 400

    conn = None
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            sql = """INSERT INTO carsharetrips 
                     (user_id, departure, departureLongitude, departureLatitude, 
                     destination, destinationLongitude, destinationLatitude, price, seatsavailable, 
                     regular, monday, tuesday, wednesday, thursday, friday, saturday, sunday, 
                     date, time, moreinformation) 
                     VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
            
            params = (
                user_id, departure, dep_lng, dep_lat, destination, dest_lng, dest_lat,
                price_float, seats_int, regular,
                days_of_week['monday'], days_of_week['tuesday'], days_of_week['wednesday'],
                days_of_week['thursday'], days_of_week['friday'], days_of_week['saturday'],
                days_of_week['sunday'], trip_date_db, time_str, more_info
            )
            cursor.execute(sql, params)
            trip_id = cursor.lastrowid 
            conn.commit()

        return jsonify(message="Trip added successfully.", trip_id=trip_id), 201

    except pymysql.err.Error as db_err: # Catch PyMySQL specific errors
        return jsonify(error="Database error while adding trip.", details=str(db_err)), 500
    except Exception as e:
        return jsonify(error="An error occurred while adding the trip.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

@app.route('/trips/mine', methods=['GET'])
def get_my_trips():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']
    conn = None
    trips = []

    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            # Select all columns, assuming all are relevant for display or edit later
            sql = "SELECT * FROM carsharetrips WHERE user_id = %s ORDER BY trip_id DESC"
            cursor.execute(sql, (user_id,))
            results = cursor.fetchall()

            for row in results:
                # Format date and time fields
                if row.get('date') and isinstance(row['date'], datetime): # Or datetime.date from some drivers
                    row['date'] = row['date'].strftime('%Y-%m-%d')
                elif row.get('date') and isinstance(row['date'], timedelta): # Should not happen for DATE type
                     pass # Or handle error if date is timedelta

                if row.get('time') and isinstance(row['time'], timedelta):
                    # Convert timedelta (from SQL TIME type) to HH:MM string
                    total_seconds = int(row['time'].total_seconds())
                    hours = total_seconds // 3600
                    minutes = (total_seconds % 3600) // 60
                    row['time'] = f"{hours:02d}:{minutes:02d}"
                
                # Convert boolean-like integers (0/1) for days to actual booleans or keep as 0/1
                # For now, keeping them as 0/1 as returned by DB for consistency with add_trip
                # days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                # for day_key in days:
                #     if day_key in row:
                #         row[day_key] = bool(row[day_key]) if row[day_key] is not None else None


                trips.append(row)
        
        return jsonify(trips=trips), 200

    except Exception as e:
        return jsonify(error="An error occurred while fetching trips.", details=str(e)), 500
    finally:
        if conn:
            conn.close()

# Configuration for file uploads
UPLOAD_FOLDER_RELATIVE = '../profilepicture/' # Relative to app.py in python_backend
# Get the absolute path to the upload folder
BASE_DIR = os.path.abspath(os.path.dirname(__file__))
UPLOAD_FOLDER_ABSOLUTE = os.path.join(BASE_DIR, UPLOAD_FOLDER_RELATIVE)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER_ABSOLUTE
app.config['ALLOWED_EXTENSIONS'] = {'png', 'jpg', 'jpeg', 'gif'}

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

@app.route('/update-profile-picture', methods=['POST'])
def update_profile_picture():
    if 'user_id' not in session:
        return jsonify(error="Unauthorized access. Please log in."), 401

    user_id = session['user_id']

    if 'profile_pic' not in request.files:
        return jsonify(error="No profile picture file provided."), 400
    
    file = request.files['profile_pic']

    if file.filename == '':
        return jsonify(error="No selected file."), 400

    if file and allowed_file(file.filename):
        original_filename = secure_filename(file.filename)
        extension = original_filename.rsplit('.', 1)[1].lower()
        # Create a more unique filename to avoid conflicts and make it harder to guess
        unique_filename = f"{user_id}_{str(uuid.uuid4())[:13]}.{extension}"
        
        # Ensure the upload folder exists
        if not os.path.exists(app.config['UPLOAD_FOLDER']):
            try:
                os.makedirs(app.config['UPLOAD_FOLDER'])
            except OSError as e: # Guard against race condition
                if not os.path.isdir(app.config['UPLOAD_FOLDER']):
                    return jsonify(error=f"Failed to create upload directory: {str(e)}"), 500
        
        file_save_path = os.path.join(app.config['UPLOAD_FOLDER'], unique_filename)
        db_path = f"profilepicture/{unique_filename}" # Path to store in DB

        conn = None
        try:
            conn = get_db_connection()
            with conn.cursor() as cursor:
                # Get current profile picture path to delete later
                cursor.execute("SELECT profilepicture FROM users WHERE user_id = %s", (user_id,))
                user_record = cursor.fetchone()
                old_db_path = user_record['profilepicture'] if user_record else None

                # Update database with new picture path
                update_sql = "UPDATE users SET profilepicture = %s WHERE user_id = %s"
                cursor.execute(update_sql, (db_path, user_id))
                conn.commit()

            # Save the new file
            file.save(file_save_path)

            # Delete old profile picture if it existed and is different
            if old_db_path and old_db_path != db_path:
                # Construct absolute path for old file, assuming it also used UPLOAD_FOLDER_RELATIVE structure
                # old_db_path is like "profilepicture/old_image.png"
                # We need to go from app.py -> .. -> profilepicture/old_image.png
                old_file_server_path_relative = os.path.join('..', old_db_path) # e.g. ../profilepicture/old_image.png
                old_file_server_path_absolute = os.path.join(BASE_DIR, old_file_server_path_relative)
                
                if os.path.exists(old_file_server_path_absolute):
                    try:
                        os.remove(old_file_server_path_absolute)
                    except OSError as e:
                        # Log this error, but don't fail the request because of it
                        app.logger.error(f"Error deleting old profile picture {old_file_server_path_absolute}: {e}")
            
            return jsonify(message="Profile picture updated successfully.", new_picture_path=db_path), 200

        except Exception as e:
            # If DB update or file save fails, try to clean up saved file if it exists
            if os.path.exists(file_save_path):
                try:
                    os.remove(file_save_path)
                except OSError as clean_err:
                     app.logger.error(f"Error cleaning up partially uploaded file {file_save_path}: {clean_err}")
            return jsonify(error="An error occurred while updating profile picture.", details=str(e)), 500
        finally:
            if conn:
                conn.close()
    else:
        return jsonify(error="Invalid file type. Allowed types are png, jpg, jpeg, gif."), 400

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5001) # Changed port for clarity if running alongside other services
